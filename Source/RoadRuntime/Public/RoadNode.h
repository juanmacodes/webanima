#pragma once

#include "CoreMinimal.h"
#include "GameFramework/Actor.h"
#include "RoadNode.generated.h"

class ARoadSegment;
class USceneComponent;

UCLASS()
class ARoadNode : public AActor
{
    GENERATED_BODY()

public:
    ARoadNode();

    void AddConnectedSegment(ARoadSegment* Segment);
    void RemoveConnectedSegment(ARoadSegment* Segment);
    const TArray<TWeakObjectPtr<ARoadSegment>>& GetConnectedSegments() const;

protected:
    virtual void EndPlay(const EEndPlayReason::Type EndPlayReason) override;

private:
    UPROPERTY(VisibleAnywhere, Category="Road", meta=(AllowPrivateAccess="true"))
    USceneComponent* SceneComponent;

    UPROPERTY(Transient)
    TArray<TWeakObjectPtr<ARoadSegment>> ConnectedSegments;
};
